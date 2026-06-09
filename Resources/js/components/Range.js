// @flow
import React from 'react';
import { computed } from 'mobx';
import rangeStyles from './range.scss';
import { uuid } from '../utils';


export default class Range extends React.Component {
  constructor(props) {
    super(props);

    const { value,
      min,
      max,
      step,
    } = this.props;


    if (typeof min === 'number' && typeof max === 'number' && min > max) {
      throw new Error('The "min" value must be higher number than "max"!');
    }

    if (typeof step === 'number' && step > (max - min)) {
      throw new Error('The "step" value must be equal or less than difference between "max" and "min"!');
    }

    if (value === undefined) {
      return;
    }

    this.listId = `range-${uuid()}`;
  }

  @computed get stops() {
    return (this.props.max - this.props.min) / this.props.step;
  }

  @computed get list() {
    const {
      showTicks: ticks,
      min,
      max,
      step,
      marks,
    } = this.props;
    const stops = this.stops;

    if (true === ticks) {

      const tickMarks = marks?.map(mark => {
        return { value: mark?.name, label: mark?.title };
      }) ?? [];

      const marksIndexes = marks?.map(mark => mark?.name);

      for (let i = 0; i <= stops; i++) {
        const value = (step * i) + min;

        if (!marksIndexes?.includes(value)) {
          tickMarks.push({ value: value });
        }
      }
      return tickMarks.sort((a, b) => a?.value - b?.value);

      // no ticks but has marks
    } else if (marks?.length > 0) {
      return marks.map(mark => {
        return { value: mark.name, label: mark.title };
      });
    }
  }

  handleChange = (value) => {
    const { onChange } = this.props;

    onChange(value.target.value);
  }

  render() {
    const {
      id,
      disabled,
      value,
      labels,
      min,
      max,
      step,
    } = this.props;
    const hasList = this.list !== undefined;
    const listId = this.listId;

    const stops = this.stops;
    const rangeValue = value ?? min;

    return (
      <div className={rangeStyles.range}
        style={{
          width: labels ? 'calc(100% - 100px)' : undefined, '--stops': stops, '--tracks': stops + 1, '--max': max, '--min': min
        }}
      >
        {labels ?
          <>
            <div className={rangeStyles.titleMin}>{min}</div>
            <div className={rangeStyles.titleMax}>{max}</div>
          </>
          : ''}
        <div className={rangeStyles.rangeWrap}>
          <input
            className={rangeStyles.rangeInput}
            style={{ '--val': rangeValue }}
            type='range'
            disabled={!!disabled}
            id={id}
            min={min}
            max={max}
            step={step}
            list={hasList ? listId : ''}
            onChange={this.handleChange}
            value={rangeValue}
          />
        </div>
        {
          hasList && (
            <div id={listId} className={rangeStyles.ticks}>
              {this.list.map((item, index) => <p
                style={{ '--offset': (((item?.value - min) / step) + 1), }}
                title={item?.label}
                key={index}>{item?.label}</p>)}
            </div>
          )
        }
      </div>
    );
  }
}
